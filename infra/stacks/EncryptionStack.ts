import { Construct } from "constructs";
import { TerraformStack } from "cdktf";
import { KmsKey } from "@cdktf/provider-aws/lib/kms-key";
import { KmsAlias } from "@cdktf/provider-aws/lib/kms-alias";
import { AwsProvider } from "@cdktf/provider-aws/lib/provider";

interface EncryptionStackProps {
    region: string;
}

export class EncryptionStack extends TerraformStack {
    public readonly kmsKey: KmsKey;
    public readonly kmsKeyAlias: KmsAlias;

    constructor(scope: Construct, id: string, { region }: EncryptionStackProps) {
        super(scope, id);
        
        new AwsProvider(this, "AWS", {
            region,
        });

        this.kmsKey = new KmsKey(this, "kms-key", {
            description: "KMS key for encryption",
        });

        this.kmsKeyAlias = new KmsAlias(this, "kms-alias", {
            name: "alias/moooood-kms-key",
            targetKeyId: this.kmsKey.keyId,
        });
    }
}

export default EncryptionStack;